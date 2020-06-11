ARCH=$$(ARCH)
isEmpty (ARCH) {
	ARCH=PC
}
message("Creating Makefile for $$ARCH")

ZLIB_PATH=../zlib
LIB_EXT=.so
REDSTONE_BUILD_DIR=$$(REDSTONE_BUILD_DIR)
isEmpty (REDSTONE_BUILD_DIR) {
	LIB_EXT=.a
	ZLIB_PATH=../../3rd-party/zlib-1.2.5
}

FASTTRAC_DIR="FastTrac"

QT	+=	core \
		network
QT	-=	gui

TARGET = packetizer-trulink

TEMPLATE = app
CONFIG += console

QMAKE_CFLAGS_RELEASE -= -O2
QMAKE_CXXFLAGS_RELEASE -= -O2
QMAKE_CFLAGS_RELEASE += -Os
QMAKE_CXXFLAGS_RELEASE += -Os

SOURCES += main.cpp \
	TruLinkDB.cpp \
	TruLinkStateMachine.cpp \
	TruLinkCopy.cpp \
	packetizerCellSender.cpp \
	../packetizer-lib/midDB.cpp \
	../message-assembler/messagedatabase.cpp \
	packetizerIridiumMessage.cpp 

HEADERS += \
	TruLinkCopy.h \
	packetizerCellSender.h \
	TruLinkDB.h \
	TruLinkStateMachine.h \
	../packetizer-lib/packetizer.h \
	../packetizer-lib/midDB.h \
	../message-assembler/messagedatabase.h \
	TruLinkCopy.h \
	packetizerIridiumMessage.h \
	packetizerCellSender.h \
	TruLinkStateMachine.h \
	calampsusermessage.h \
	calampsmessage.h \
	calampseventmessage.h \
	calampsackmessage.h \
	calampscompressedmessage.h \
	TruLink.h

LIBS += -L../socket_interface/bin/$$ARCH/ -lsocket_interface \
	-L../command_line_parser/bin/$$ARCH/ -lcommand_line_parser \
	-L../$$FASTTRAC_DIR/FASTLib/bin/$$ARCH/ -lFASTLib \
	-L../$$FASTTRAC_DIR/UtilityLib/bin/$$ARCH/ -lUtilityLib \
	-L../state-machine/bin/$$ARCH/ -lstate-machine \
	-L../db-monitor/bin/$$ARCH/ -ldb-monitor \
	-L../ats-common/bin/$$ARCH/ -lats-common \
	-L../packetizer-lib/bin/$$ARCH/ -lpacketizer \
	-L../iridium-monitor/lib/bin/$$ARCH/ -liridium \
	-L../TLP/bin/$$ARCH/ -lTLP \
	-L$$ZLIB_PATH -lz \
	-L../FastTrac/boost/stage/lib/ -lboost_system

INCLUDEPATH += ../ats-common \
	../socket_interface \
	../command_line_parser \
	../$$FASTTRAC_DIR/FASTLib \
	../$$FASTTRAC_DIR/UtilityLib \
	../$$FASTTRAC_DIR \
	../state-machine \
	../db-monitor \
	../message-assembler \
	../packetizer-lib \
	../iridium-monitor \
	../TLP \
	$$ZLIB_PATH

DEPENDPATH += += ../ats-common \
	../socket_interface \
	../command_line_parser \
	../$$FASTTRAC_DIR/FASTLib \
	../$$FASTTRAC_DIR/UtilityLib \
	../$$FASTTRAC_DIR \
	../state-machine \
	../db-monitor \
	../message-assembler

PRE_TARGETDEPS += ../ats-common/bin/$$ARCH/libats-common$$LIB_EXT \
	../socket_interface/bin/$$ARCH/libsocket_interface$$LIB_EXT \
	../command_line_parser/bin/$$ARCH/libcommand_line_parser$$LIB_EXT \
	../$$FASTTRAC_DIR/FASTLib/bin/$$ARCH/libFASTLib.a \
	../$$FASTTRAC_DIR/UtilityLib/bin/$$ARCH/libUtilityLib.a \
	../state-machine/bin/$$ARCH/libstate-machine$$LIB_EXT \
	../iridium-monitor/lib/bin/$$ARCH/libiridium$$LIB_EXT \
	../db-monitor/bin/$$ARCH/libdb-monitor$$LIB_EXT