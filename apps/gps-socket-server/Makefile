ARCH=PC
KERNEL_SRC_DIR='../mx28/linux-2.6.35.3'

INCLUDE =\
	-I../ats-common \
        -I${KERNEL_SRC_DIR}/include \
	-I../socket_interface \
	-I../FastTrac/UtilityLib \
	-I../FastTrac/FASTLib \
	-I../FastTrac/ \
	-I../command_line_parser \
	-I../db-monitor

LD_FLAGS=\
	-L../socket_interface/bin/$(ARCH) \
	-L../command_line_parser/bin/$(ARCH) \
	-L../ats-common/bin/$(ARCH) \
	-L../db-monitor/bin/$(ARCH) \
	-L../FastTrac/UtilityLib/bin/$(ARCH) \
	-L../FastTrac/FASTLib/bin/$(ARCH) \
	-lats-common \
	-lsocket_interface \
	-lcommand_line_parser \
	-ldb-monitor \
	-lpthread \
	-lrt \
	-s

all: $(ARCH)

PC:
	mkdir -p bin/$(ARCH)
	g++ -o bin/$(ARCH)/gps-socket-server main.cpp -Wall -Os $(INCLUDE) $(LD_FLAGS)

arm:
	mkdir -p bin/$(ARCH)
	${CROSS_COMPILE}g++ -o bin/$(ARCH)/gps-socket-server main.cpp -Wall -Os $(INCLUDE) $(LD_FLAGS)

clean:
	rm -rf bin

